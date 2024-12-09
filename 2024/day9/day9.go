package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		fileContentsSplit := strings.Split(string(fileContents), "")
		fileContentsInt := make([]int, len(fileContentsSplit))
		diskSize := 0
		for index, value := range fileContentsSplit {
			fileContentsInt[index], _ = strconv.Atoi(value)
			diskSize += fileContentsInt[index]
		}
		//int for file id, -1 for free space
		var fileSystemContents = make([]int, diskSize)
		var cursorIsOnFile = true
		var fileSystemPosition = 0
		var fileIndex = -1
		for _, value := range fileContentsInt {
			if cursorIsOnFile {
				fileIndex++
			}
			nextFileStart := fileSystemPosition + value
			for ; fileSystemPosition < nextFileStart; fileSystemPosition++ {
				if cursorIsOnFile {
					fileSystemContents[fileSystemPosition] = fileIndex
				} else {
					fileSystemContents[fileSystemPosition] = -1 //free space
				}
			}
			cursorIsOnFile = !cursorIsOnFile
		}
		lastFreeSpaceInTheStart := 0
		firstNonFreeSpaceInTheEnd := len(fileSystemContents) - 1
		for {
			lastFreeSpaceInTheStart, firstNonFreeSpaceInTheEnd = defragOneBlock(lastFreeSpaceInTheStart, firstNonFreeSpaceInTheEnd, &fileSystemContents)
			if lastFreeSpaceInTheStart == -1 || firstNonFreeSpaceInTheEnd == -1 {
				break
			}
		}
		var total = 0
		for index, value := range fileSystemContents {
			if value != -1 {
				total += index * value
			}
		}
		println(total)
	}
}
func defragOneBlock(lastFreeSpaceInTheStart int, firstNonFreeSpaceInTheEnd int, diskContents *[]int) (int, int) {

	var blockToMove = -1
	var freeSpace = -1
	for index := firstNonFreeSpaceInTheEnd; index > lastFreeSpaceInTheStart; index-- {
		if (*diskContents)[index] == -1 {
			continue
		}
		blockToMove = index
		break
	}
	for index := lastFreeSpaceInTheStart; index < firstNonFreeSpaceInTheEnd; index++ {
		if (*diskContents)[index] != -1 {
			continue
		}
		freeSpace = index
		break
	}
	if blockToMove == -1 || freeSpace == -1 {
		return -1, -1
	}
	(*diskContents)[freeSpace] = (*diskContents)[blockToMove]
	(*diskContents)[blockToMove] = -1

	return freeSpace, blockToMove

}

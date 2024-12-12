package main

import (
	"fmt"
	"os"
	"slices"
	"strings"
)

type coordinates struct {
	x, y int
}

var maxX, maxY int
var gardenMap [][]string

func main() {
	filenames := []string{"testInput", "testInput2", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		total := 0
		gardenMap = make([][]string, len(lines))
		maxX = len(lines[0]) - 1
		maxY = len(lines) - 1
		for y, line := range lines {
			gardenMap[y] = strings.Split(line, "")
		}
		regions := make([][]coordinates, 0)
		for y := 0; y < len(lines); y++ {
		searchRegion:
			for x := 0; x < len(lines[y]); x++ {
				plotCoordinates := coordinates{x, y}
				for _, region := range regions {
					if slices.Contains(region, plotCoordinates) {
						continue searchRegion
					}
				}
				checkedPlots := make([]coordinates, 0)
				region := findAllPlotsInARegion(plotCoordinates, &checkedPlots)
				regions = append(regions, region)
				area := len(region)
				perimeter := 0
				for _, plot := range region {
					perimeter += plotContributionToPerimeter(plot)
				}
				total += area * perimeter
			}
		}
		println(total)
	}
}
func findAllPlotsInARegion(startingPlot coordinates, checkedPlots *[]coordinates) []coordinates {
	result := make([]coordinates, 1)
	result[0] = startingPlot
	*checkedPlots = append(*checkedPlots, startingPlot)
	plantAtPlot := gardenMap[startingPlot.y][startingPlot.x]

	if startingPlot.x > 0 {
		plotToTheLeft := coordinates{startingPlot.x - 1, startingPlot.y}
		if gardenMap[plotToTheLeft.y][plotToTheLeft.x] == plantAtPlot && !slices.Contains(*checkedPlots, plotToTheLeft) {
			result = append(result, findAllPlotsInARegion(plotToTheLeft, checkedPlots)...)
		}
	}
	if startingPlot.x < maxX {
		plotToTheRight := coordinates{startingPlot.x + 1, startingPlot.y}
		if gardenMap[plotToTheRight.y][plotToTheRight.x] == plantAtPlot && !slices.Contains(*checkedPlots, plotToTheRight) {
			result = append(result, findAllPlotsInARegion(plotToTheRight, checkedPlots)...)
		}
	}
	if startingPlot.y > 0 {
		plotToTheTop := coordinates{startingPlot.x, startingPlot.y - 1}
		if gardenMap[plotToTheTop.y][plotToTheTop.x] == plantAtPlot && !slices.Contains(*checkedPlots, plotToTheTop) {
			result = append(result, findAllPlotsInARegion(plotToTheTop, checkedPlots)...)
		}
	}
	if startingPlot.y < maxY {
		plotToTheBottom := coordinates{startingPlot.x, startingPlot.y + 1}
		if gardenMap[plotToTheBottom.y][plotToTheBottom.x] == plantAtPlot && !slices.Contains(*checkedPlots, plotToTheBottom) {
			result = append(result, findAllPlotsInARegion(plotToTheBottom, checkedPlots)...)
		}
	}

	return result
}

func plotContributionToPerimeter(plotCoordinates coordinates) int {
	plantAtPlot := gardenMap[plotCoordinates.y][plotCoordinates.x]
	perimeter := 0
	if plotCoordinates.x == 0 {
		perimeter++
	} else {
		if gardenMap[plotCoordinates.y][plotCoordinates.x-1] != plantAtPlot {
			perimeter++
		}
	}
	if plotCoordinates.x == maxX {
		perimeter++
	} else if gardenMap[plotCoordinates.y][plotCoordinates.x+1] != plantAtPlot {
		perimeter++
	}
	if plotCoordinates.y == 0 {
		perimeter++
	} else {
		if gardenMap[plotCoordinates.y-1][plotCoordinates.x] != plantAtPlot {
			perimeter++
		}
	}
	if plotCoordinates.y == maxY {
		perimeter++
	} else if gardenMap[plotCoordinates.y+1][plotCoordinates.x] != plantAtPlot {
		perimeter++
	}

	return perimeter
}
